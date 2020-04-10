package th.ac.dusit.dbizcom.chainattourism.fragment;

import android.content.Context;
import android.graphics.Rect;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.widget.CircularProgressDrawable;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
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

import th.ac.dusit.dbizcom.chainattourism.R;
import th.ac.dusit.dbizcom.chainattourism.etc.Utils;
import th.ac.dusit.dbizcom.chainattourism.model.District;

public class OtopSearchFragment extends Fragment {

    private OtopSearchFragmentListener mListener;

    private District[] mDistrictList = new District[]{
            new District(1, "เมืองชัยนาท", new String[]{"ชัยนาท", "ธรรมามูล"}, R.drawable.district_meung),
            new District(2, "หันคา", new String[]{"บ้านเชี่ยน"}, R.drawable.district_hunca),
            new District(3, "สรรพยา", new String[]{"หาดอาสา"}, R.drawable.district_saphaya),
            new District(4, "เนินขาม", new String[]{"เนินขาม"}, R.drawable.district_nernkam),
            new District(5, "มโนรมย์", new String[]{"ศิลาดาน"}, R.drawable.district_manorom),
            new District(6, "สรรคบุรี", new String[]{"แพรกศรีราชา", "บางขุด"}, R.drawable.district_sankaburi),
            new District(7, "วัดสิงห์", new String[]{"มะขามเฒ่า"}, R.drawable.district_wadsing),
            new District(8, "หนองมะโมง", new String[]{"กุดจอก", "วังตะเคียน"}, R.drawable.district_nongmamong)
    };

    private View mProgressView;
    private RecyclerView mPlaceListRecyclerView;

    public OtopSearchFragment() {
        // Required empty public constructor
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_otop_search, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        ImageView backImageView = view.findViewById(R.id.back_image_view);
        backImageView.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mListener != null) {
                    mListener.onClickBack();
                }
            }
        });

        TextView titleTextView = view.findViewById(R.id.title_text_view);
        titleTextView.setText("สินค้า OTOP");

        final EditText searchEditText = view.findViewById(R.id.search_edit_text);
        setupSearchEditText(searchEditText);

        titleTextView.setText("ค้นหาสินค้า OTOP");

        mProgressView = view.findViewById(R.id.progress_view);
        setupRecyclerView(view);
    }

    private void setupSearchEditText(final EditText searchEditText) {
        searchEditText.setOnTouchListener(new View.OnTouchListener() {
            @Override
            public boolean onTouch(View v, MotionEvent event) {
                final int DRAWABLE_LEFT = 0;
                final int DRAWABLE_TOP = 1;
                final int DRAWABLE_RIGHT = 2;
                final int DRAWABLE_BOTTOM = 3;

                if (event.getAction() == MotionEvent.ACTION_UP) {
                    if (event.getRawX() >= (searchEditText.getRight() - (searchEditText.getCompoundDrawables()[DRAWABLE_RIGHT].getBounds().width() + Utils.convertDpToPixel(16, getContext())))) {
                        //searchEditText.setText("");

                        String searchText = searchEditText.getText().toString().trim();
                        //Utils.showOkDialog(getActivity(), "Test", searchText, null);
                        if (!searchText.trim().isEmpty()) {
                            if (mListener != null) {
                                mListener.onSearchOtop(searchText);
                            }
                        } else {
                            searchEditText.setText(searchText.trim());
                            Utils.showOkDialog(getActivity(), "ผิดพลาด", "กรอกคำที่ต้องการค้นหา", null);
                        }
                        return true;
                    }
                }
                return false;
            }
        });
    }

    private void setupRecyclerView(View view) {
        mPlaceListRecyclerView = view.findViewById(R.id.place_list_recycler_view);

        DistrictListAdapter adapter = new DistrictListAdapter(
                getContext(),
                mDistrictList,
                mListener
        );
        mPlaceListRecyclerView.setLayoutManager(new LinearLayoutManager(view.getContext()));
        mPlaceListRecyclerView.addItemDecoration(new SpacingDecoration(view.getContext()));
        mPlaceListRecyclerView.setAdapter(adapter);
    }

    @Override
    public void onAttach(Context context) {
        super.onAttach(context);
        if (context instanceof OtopSearchFragmentListener) {
            mListener = (OtopSearchFragmentListener) context;
        } else {
            throw new RuntimeException(context.toString()
                    + " must implement OtopSearchFragmentListener");
        }
    }

    @Override
    public void onDetach() {
        super.onDetach();
        mListener = null;
    }

    public interface OtopSearchFragmentListener {
        void onClickDistrict(District district);
        void onSearchOtop(String searchTerm);
        void onClickBack();
    }

    private static class DistrictListAdapter extends RecyclerView.Adapter<DistrictListAdapter.ViewHolder> {

        private final Context mContext;
        private final District[] mDistrictList;
        private final OtopSearchFragmentListener mListener;

        DistrictListAdapter(Context context, District[] districtList, OtopSearchFragmentListener listener) {
            mContext = context;
            mDistrictList = districtList;
            mListener = listener;
        }

        @NonNull
        @Override
        public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext()).inflate(
                    R.layout.item_district, parent, false
            );
            return new ViewHolder(view);
        }

        @Override
        public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
            final District district = mDistrictList[position];

            holder.mDistrict = district;
            holder.mDistrictNameTextView.setText("อำเภอ".concat(district.name));

            String subDistrictText = "";
            int i = 0;
            for (String subDistrict : district.subDistrictNameList) {
                if (i++ == 0) {
                    subDistrictText = "ตำบล".concat(subDistrict);
                } else {
                    subDistrictText = subDistrictText.concat(", ตำบล".concat(subDistrict));
                }
            }
            holder.mSubDistrictTextView.setText(subDistrictText);

            CircularProgressDrawable circularProgressDrawable = new CircularProgressDrawable(mContext);
            circularProgressDrawable.setStrokeWidth(5f);
            circularProgressDrawable.setCenterRadius(30f);
            circularProgressDrawable.start();

            Glide.with(mContext)
                    .load(district.imageRes)
                    .placeholder(circularProgressDrawable)
                    .into(holder.mDistrictImageView);
        }

        @Override
        public int getItemCount() {
            return mDistrictList.length;
        }

        class ViewHolder extends RecyclerView.ViewHolder {

            private final View mRootView;
            private final TextView mDistrictNameTextView;
            private final TextView mSubDistrictTextView;
            private final ImageView mDistrictImageView;

            private District mDistrict;

            ViewHolder(View itemView) {
                super(itemView);

                mRootView = itemView;
                mDistrictNameTextView = itemView.findViewById(R.id.district_name_text_view);
                mSubDistrictTextView = itemView.findViewById(R.id.sub_district_text_view);
                mDistrictImageView = itemView.findViewById(R.id.district_image_view);

                mRootView.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        mListener.onClickDistrict(mDistrict);
                    }
                });
            }
        }
    }

    public class SpacingDecoration extends RecyclerView.ItemDecoration {

        private final static int MARGIN_TOP_IN_DP = 72;
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
                outRect.top = mMarginTop;
                //outRect.top = 0;
            }
            final RecyclerView.Adapter adapter = parent.getAdapter();
            if ((adapter != null) && (itemPosition == adapter.getItemCount() - 1)) {
                outRect.bottom = mMarginBottom;
            }
        }
    }
}
