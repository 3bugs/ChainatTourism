package th.ac.dusit.dbizcom.chainattourism.fragment;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Rect;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.text.Editable;
import android.text.TextWatcher;
import android.util.DisplayMetrics;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;

import com.bumptech.glide.Glide;

import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Retrofit;
import th.ac.dusit.dbizcom.chainattourism.R;
import th.ac.dusit.dbizcom.chainattourism.etc.Utils;
import th.ac.dusit.dbizcom.chainattourism.model.Place;
import th.ac.dusit.dbizcom.chainattourism.net.ApiClient;
import th.ac.dusit.dbizcom.chainattourism.net.GetPlaceResponse;
import th.ac.dusit.dbizcom.chainattourism.net.MyRetrofitCallback;
import th.ac.dusit.dbizcom.chainattourism.net.WebServices;

public class PlaceListFragment extends Fragment {

    private static final String ARG_PLACE_TYPE = "place_type";

    private Place.PlaceType mPlaceType;
    private List<Place> mPlaceList = null;
    private PlaceListAdapter mAdapter;
    private boolean mIsFiltered = false;

    private PlaceListFragmentListener mListener;

    private View mProgressView;
    private RecyclerView mPlaceListRecyclerView;

    public PlaceListFragment() {
        // Required empty public constructor
    }

    public static PlaceListFragment newInstance(Place.PlaceType placeType) {
        Bundle args = new Bundle();
        args.putSerializable(ARG_PLACE_TYPE, placeType);
        PlaceListFragment fragment = new PlaceListFragment();
        fragment.setArguments(args);
        return fragment;
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        Bundle args = getArguments();
        if (args != null) {
            mPlaceType = (Place.PlaceType) args.getSerializable(ARG_PLACE_TYPE);
        }
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_place_list, container, false);
    }

    @SuppressLint("ClickableViewAccessibility")
    @Override
    public void onViewCreated(@NonNull final View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        TextView titleTextView = view.findViewById(R.id.title_text_view);
        final EditText searchEditText = view.findViewById(R.id.search_edit_text);

        String placeTypeText = null;

        if (mPlaceType == Place.PlaceType.TOUR) {
            placeTypeText = view.getResources().getString(R.string.place_type_tour);
        } else if (mPlaceType == Place.PlaceType.TEMPLE) {
            placeTypeText = view.getResources().getString(R.string.place_type_temple);
        } else if (mPlaceType == Place.PlaceType.RESTAURANT) {
            placeTypeText = view.getResources().getString(R.string.place_type_restaurant);
        } else if (mPlaceType == Place.PlaceType.OTOP) {
            placeTypeText = view.getResources().getString(R.string.place_type_otop);
        }

        titleTextView.setText(placeTypeText);
        searchEditText.setHint("ค้นหา" + placeTypeText);

        searchEditText.setOnTouchListener(new View.OnTouchListener() {
            @Override
            public boolean onTouch(View v, MotionEvent event) {
                final int DRAWABLE_LEFT = 0;
                final int DRAWABLE_TOP = 1;
                final int DRAWABLE_RIGHT = 2;
                final int DRAWABLE_BOTTOM = 3;

                if (event.getAction() == MotionEvent.ACTION_UP) {
                    if (event.getRawX() >= (searchEditText.getRight() - (searchEditText.getCompoundDrawables()[DRAWABLE_RIGHT].getBounds().width() + Utils.convertDpToPixel(16, view.getContext())))) {
                        searchEditText.setText("");
                        if (mAdapter != null) {
                            mAdapter.search(null);
                        }

                        /*String searchText = searchEditText.getText().toString().trim();
                        if (mIsFiltered) {
                            //searchEditText.setText("");
                            mAdapter.search(null);
                            searchEditText.setCompoundDrawablesWithIntrinsicBounds(0, 0, R.drawable.ic_search, 0);
                            mIsFiltered = false;
                        } else {
                            if (searchText.isEmpty()) {
                                searchEditText.setError("ใส่คำที่ต้องการค้นหา");
                            } else {
                                if (mAdapter != null) {
                                    mAdapter.search(searchText);
                                    searchEditText.setCompoundDrawablesWithIntrinsicBounds(0, 0, R.drawable.ic_close, 0);
                                    mIsFiltered = true;
                                }
                            }
                        }*/
                        return true;
                    }
                }
                return false;
            }
        });

        searchEditText.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence charSequence, int i, int i1, int i2) {

            }

            @Override
            public void onTextChanged(CharSequence charSequence, int i, int i1, int i2) {
                String searchText = charSequence.toString().trim();
                if (mAdapter != null) {
                    mAdapter.search(searchText);
                }
                if (searchText.isEmpty()) {
                    searchEditText.setCompoundDrawablesWithIntrinsicBounds(0, 0, R.drawable.ic_search, 0);
                } else {
                    searchEditText.setCompoundDrawablesWithIntrinsicBounds(0, 0, R.drawable.ic_close, 0);
                }
            }

            @Override
            public void afterTextChanged(Editable editable) {

            }
        });

        mPlaceListRecyclerView = view.findViewById(R.id.place_list_recycler_view);
        mProgressView = view.findViewById(R.id.progress_view);

        if (mPlaceList == null) {
            doGetPlace();
        } else {
            setupRecyclerView();
        }
    }

    private void doGetPlace() {
        mProgressView.setVisibility(View.VISIBLE);

        Retrofit retrofit = ApiClient.getClient();
        WebServices services = retrofit.create(WebServices.class);

        Call<GetPlaceResponse> call = services.getPlace(mPlaceType);
        call.enqueue(new MyRetrofitCallback<>(
                getActivity(),
                null,
                mProgressView,
                new MyRetrofitCallback.MyRetrofitCallbackListener<GetPlaceResponse>() {
                    @Override
                    public void onSuccess(GetPlaceResponse responseBody) {
                        mPlaceList = responseBody.placeList;
                        setupRecyclerView();
                    }

                    @Override
                    public void onError(String errorMessage) {

                    }
                }
        ));
    }

    private void setupRecyclerView() {
        if (getContext() != null) {
            mAdapter = new PlaceListAdapter(
                    getContext(),
                    mPlaceList,
                    mListener
            );
            mPlaceListRecyclerView.setLayoutManager(new LinearLayoutManager(getContext()));
            mPlaceListRecyclerView.addItemDecoration(new SpacingDecoration(getContext()));
            mPlaceListRecyclerView.setAdapter(mAdapter);
        }
    }

    @Override
    public void onAttach(Context context) {
        super.onAttach(context);
        if (context instanceof PlaceListFragmentListener) {
            mListener = (PlaceListFragmentListener) context;
        } else {
            throw new RuntimeException(context.toString()
                    + " must implement PlaceListFragmentListener");
        }
    }

    @Override
    public void onDetach() {
        super.onDetach();
        mListener = null;
    }

    public interface PlaceListFragmentListener {
        void onClickPlace(Place place);
    }

    private static class PlaceListAdapter extends RecyclerView.Adapter<PlaceListFragment.PlaceListAdapter.PlaceViewHolder> {

        private final Context mContext;
        private final List<Place> mOriginalPlaceList = new ArrayList<>();
        private final List<Place> mPlaceList;
        private final PlaceListFragmentListener mListener;

        PlaceListAdapter(Context context, List<Place> placeList, PlaceListFragmentListener listener) {
            mContext = context;
            mPlaceList = placeList;
            mOriginalPlaceList.addAll(placeList);
            mListener = listener;
        }

        @NonNull
        @Override
        public PlaceViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext()).inflate(
                    R.layout.item_place, parent, false
            );
            return new PlaceViewHolder(view);
        }

        @Override
        public void onBindViewHolder(@NonNull PlaceViewHolder holder, int position) {
            final Place place = mPlaceList.get(position);

            holder.mPlace = place;
            holder.mNameTextView.setText(place.name);
            holder.mDistrictTextView.setText(place.district);
            Glide.with(mContext)
                    .load(ApiClient.IMAGE_BASE_URL + place.listImage)
                    .into(holder.mPlaceImageView);
        }

        @Override
        public int getItemCount() {
            return mPlaceList.size();
        }

        public void search(String searchText) {
            List<Place> placeList = new ArrayList<>();

            for (Place place : mOriginalPlaceList) {
                if (searchText == null || searchText.trim().isEmpty()
                        || place.name.contains(searchText) || place.district.contains(searchText)) {
                    placeList.add(place);
                }
            }

            mPlaceList.clear();
            mPlaceList.addAll(placeList);
            notifyDataSetChanged();
        }

        class PlaceViewHolder extends RecyclerView.ViewHolder {

            private final View mRootView;
            private final TextView mNameTextView;
            private final TextView mDistrictTextView;
            private final ImageView mPlaceImageView;

            private Place mPlace;

            PlaceViewHolder(View itemView) {
                super(itemView);

                mRootView = itemView;
                mNameTextView = itemView.findViewById(R.id.place_name_text_view);
                mDistrictTextView = itemView.findViewById(R.id.district_text_view);
                mPlaceImageView = itemView.findViewById(R.id.place_image_view);

                mPlaceImageView.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        mListener.onClickPlace(mPlace);
                    }
                });
            }
        }
    }

    public class SpacingDecoration extends RecyclerView.ItemDecoration {

        private final static int MARGIN_TOP_IN_DP = 8;
        private final static int MARGIN_BOTTOM_IN_DP = 16;
        private final int mMarginTop, mMarginBottom;

        SpacingDecoration(@NonNull Context context) {
            DisplayMetrics metrics = context.getResources().getDisplayMetrics();
            mMarginTop = (int) TypedValue.applyDimension(
                    TypedValue.COMPLEX_UNIT_DIP,
                    MARGIN_TOP_IN_DP,
                    metrics
            );
            mMarginBottom = (int) TypedValue.applyDimension(
                    TypedValue.COMPLEX_UNIT_DIP,
                    MARGIN_BOTTOM_IN_DP,
                    metrics
            );
        }

        @Override
        public void getItemOffsets(@NonNull Rect outRect, @NonNull View view,
                                   @NonNull RecyclerView parent,
                                   @NonNull RecyclerView.State state) {
            final int itemPosition = parent.getChildAdapterPosition(view);
            if (itemPosition == RecyclerView.NO_POSITION) {
                return;
            }
            if (itemPosition == 0) {
                //outRect.top = mMarginTop;
                outRect.top = 0;
            }
            final RecyclerView.Adapter adapter = parent.getAdapter();
            if ((adapter != null) && (itemPosition == adapter.getItemCount() - 1)) {
                outRect.bottom = mMarginBottom;
            }
        }
    }
}
